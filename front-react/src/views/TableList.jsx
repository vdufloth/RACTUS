import React, { Component } from "react";
import { Grid, Row, Col, Table } from "react-bootstrap";

import Card from "components/Card/Card.jsx";
import { thArray } from "variables/Variables.jsx";

class TableList extends Component {

  state = {tdArray: []};

  componentDidMount(){
    this.getData();
    this.timer = setInterval(()=> this.getData(), 2000)
  }

  async getData(){
    fetch("http://192.168.1.204/sist-distribuidos/api/data/last?limit=20")
    .then(response => {
      return response.json()
    })
    .then(data => {
      var values = new Array(new Array());
      for(var i=0; i<data.data.length; i++) {
        values.push([data.data[i].id, data.data[i].temperature, data.data[i].humidity, data.data[i].read_at.formatted]);
      }
      values.splice(0, 1);

      this.setState({
        tdArray: values
      })
    }
    ).catch(function(error) {
      console.log(error);
    });
  }

  render() {
    return (
      <div className="content">
        <Grid fluid>
          <Row>
            <Col md={12}>
              <Card
                title="Histórico de medidas"
                category="A tabela mostra os últimos registros salvos"
                ctTableFullWidth
                ctTableResponsive
                content={
                  <Table striped hover>
                    <thead>
                      <tr>
                        {thArray.map((prop, key) => {
                          return <th key={key}>{prop}</th>;
                        })}
                      </tr>
                    </thead>
                    <tbody>
                      {this.state.tdArray.map((prop, i) => {
                        return (
                          <tr key={i}>
                            {prop.map((p, j) => {
                              console.log(p);
                              return <td key={j}>{p}</td>;
                            })}
                          </tr>
                        );
                      })}
                    </tbody>
                  </Table>
                }
              />
            </Col>
          </Row>
        </Grid>
      </div>
    );
  }
}

export default TableList;
