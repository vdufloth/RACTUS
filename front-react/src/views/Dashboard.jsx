import React, { Component, useState } from "react";
import ChartistGraph from "react-chartist";
import { Grid, Row, Col } from "react-bootstrap";

import { Card } from "components/Card/Card.jsx";
import { StatsCard } from "components/StatsCard/StatsCard.jsx";
import {
  optionsSales,
  responsiveSales,
  optionsBar,
  responsiveBar
} from "variables/Variables.jsx";

class Dashboard extends Component {
  state = {
    temperature: 0,
    humidity: 0,
    last_measure: 0,
    graphMeasures: {
      labels: [
        "00:00",
        "01:00",
        "02:00",
        "03:00",
        "04:00",
        "05:00",
        "06:00",
        "07:00",
        "08:00",
        "09:00",
        "10:00",
        "11:00",
        "12:00",
        "13:00",
        "14:00",
        "15:00",
        "16:00",
        "17:00",
        "18:00",
        "19:00",
        "20:00",
        "21:00",
        "22:00",
        "23:00",
        "24:00",
      ],
      series: [
        [],
        []
      ]
    },
    avgMonthTemperature: {},
    avgMonthHumidty: {},
    dataBar: {
      labels: [],
      series: [
        [],[]
      ]
    }
  };

  componentDidMount() {
    this.getData();
    this.timer = setInterval(() => this.getData(), 5000)
  }

  async getData() {
    fetch("http://192.168.1.204/sist-distribuidos/api/data/last")
      .then(response => {
        return response.json()
      })
      .then(data => {
        this.setState({
          temperature: data.data.temperature,
          humidity: data.data.humidity,
          last_measure: data.data.read_at.formatted
        })
      }
      ).catch(function (error) {
        console.log(error);
      });

      fetch("http://192.168.1.204/sist-distribuidos/api/data/day_average")
      .then(response => {
        return response.json()
      })
      .then(data => {
        var days = [];
        var temps = [];
        var hums = [];
        
        for(var i in data.data) {
          days.push(i);
          hums.push(data.data[i].humidity);
          temps.push(data.data[i].temperature);
        }

        var tmp = {
          labels: days,
          series: [
            hums, temps
          ]
        };

        this.setState({
          dataBar: tmp
        })
      }
      ).catch(function (error) {
        console.log(error);
      });  

    fetch("http://192.168.1.204/sist-distribuidos/api/data/hour_average")
      .then(response => {
        return response.json()
      })
      .then(data => {
        var temperatures = new Array();
        for(var i=0; i<data.data.length; i++) {
          temperatures.push(data.data[i].avg_temperature);
        }
        temperatures.splice(0, 1);

        var humidities = new Array();
        for(var i=0; i<data.data.length; i++) {
          humidities.push(data.data[i].avg_humidity);
        }
        humidities.splice(0, 1);

        var tmp = {
          labels: this.state.graphMeasures.labels,
          series: [
            humidities,
            temperatures
          ]
        };

        this.setState({
          graphMeasures: tmp
        })
      }
      ).catch(function (error) {
        console.log(error);
      });  
    

      
  }
  render() {
    return (
      <div className="content">
        <Grid fluid>
          <Row>
            <Col lg={3} sm={6}>
              <StatsCard
                bigIcon={<i className="pe-7s-eyedropper text-warning" />}
                statsText="Temperatura"
                statsValue={this.state.temperature + "º"}
                statsIcon={<i className="pe-7s-eyedropper" />}
                statsIconText={"Ultimo Registro: " + this.state.last_measure}
              />
            </Col>
            <Col lg={3} sm={6}>
              <StatsCard
                bigIcon={<i className="pe-7s-drop text-info" />}
                statsText="Umidade"
                statsValue={this.state.humidity + "%"}
                statsIcon={<i className="pe-7s-drop" />}
                statsIconText={"Ultimo Registro: " + this.state.last_measure}
              />
            </Col>
          </Row>
          {/* start gráficos */}
          <Row>
            <Col md={13}>
              <Card
                statsIcon="fa fa-history"
                id="chartHours"
                title="Temperatura e Umidade média por hora"
                category="24 Hours performance"
                stats={"Ultimo Registro: "+ this.state.last_measure}
                content={
                  <div className="ct-chart">
                    <ChartistGraph
                      data={this.state.graphMeasures}
                      type="Line"
                      options={optionsSales}
                      responsiveOptions={responsiveSales}
                    />
                  </div>
                }
              />
            </Col>
          </Row>
          <Row>
            <Col md={13}>
              <Card
                id="chartActivity"
                title="Maior Temperatura e Umidade por Dia"
                category="Todos os dias do Mês"
                content={
                  <div className="ct-chart">
                    <ChartistGraph
                      data={this.state.dataBar}
                      type="Bar"
                      options={optionsBar}
                      responsiveOptions={responsiveBar}
                    />
                  </div>
                }
              />
            </Col>
          </Row>
          {/* end gráficos */}
        </Grid>
      </div>
    );
  }
}

export default Dashboard;
